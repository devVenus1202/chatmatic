import React, {useEffect} from 'react';
import { makeStyles, withStyles } from '@material-ui/core/styles';
import Pagination from '@material-ui/lab/Pagination';
import PaginationItem from '@material-ui/lab/PaginationItem';

const CssPagination = withStyles((theme) => ({
  root: {
    '& > *': {
      marginTop: theme.spacing(2),
    },
    
  },
}))(Pagination);

const CssPaginationItem = withStyles((theme) => ({
    selected: {
        'button': {
            backgroundColor: '#0A0D28'
        }
    }
}))(PaginationItem);

export default function PaginationSize(props) {
    useEffect(()=>{
    })
  return (
      <CssPagination count={10} size="large" {...props} renderItem={item => <CssPaginationItem {...item}/>}/>
  );
}